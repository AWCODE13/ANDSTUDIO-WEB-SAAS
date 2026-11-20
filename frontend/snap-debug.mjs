import { spawn } from "node:child_process";
import { setTimeout as sleep } from "node:timers/promises";
import { writeFileSync } from "node:fs";

const CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";
const PORT = 9271;
const CODE = process.argv[2];
const TOKEN = process.argv[3];
const AMOUNT = process.argv[4];

const chrome = spawn(
  CHROME,
  [
    `--remote-debugging-port=${PORT}`,
    "--remote-allow-origins=*",
    "--headless=new",
    "--disable-gpu",
    "--no-first-run",
    "--no-sandbox",
    `--user-data-dir=/tmp/cdbg-${Date.now()}`,
    "--window-size=1440,1000",
    "about:blank",
  ],
  { stdio: "ignore" }
);

for (let i = 0; i < 30; i++) {
  await sleep(1000);
  try {
    if ((await fetch(`http://127.0.0.1:${PORT}/json/version`)).ok) break;
  } catch {}
}

const list = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
const t = list.find((x) => x.type === "page");
const ws = new WebSocket(t.webSocketDebuggerUrl);
let id = 0;
const pend = new Map();
const logs = [];
const failed = [];

const send = (m, p = {}) =>
  new Promise((r) => {
    const i = ++id;
    pend.set(i, r);
    ws.send(JSON.stringify({ id: i, method: m, params: p }));
  });

await new Promise((r) => (ws.onopen = r));

ws.onmessage = (e) => {
  const m = JSON.parse(e.data);
  if (m.id && pend.has(m.id)) {
    pend.get(m.id)(m.result);
    pend.delete(m.id);
    return;
  }
  if (m.method === "Runtime.consoleAPICalled") {
    logs.push(
      `[${m.params.type}] ` + m.params.args.map((a) => a.value ?? a.description ?? a.type).join(" ")
    );
  }
  if (m.method === "Runtime.exceptionThrown") {
    logs.push("[exception] " + (m.params.exceptionDetails.exception?.description || m.params.exceptionDetails.text));
  }
  if (m.method === "Network.loadingFailed") {
    failed.push(`${m.params.errorText} ${m.params.type}`);
  }
};

const ev = async (x) =>
  (await send("Runtime.evaluate", { expression: x, returnByValue: true, awaitPromise: true })).result.value;

await send("Page.enable");
await send("Runtime.enable");
await send("Network.enable");
await send("Emulation.setDeviceMetricsOverride", { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });

const url = `http://localhost:3000/pesanan/${CODE}/bayar?code=${CODE}&amount=${AMOUNT}&snapToken=${encodeURIComponent(TOKEN)}`;
await send("Page.navigate", { url });
await sleep(7000);

console.log("URL:", url);
console.log("snapReady(button text):", await ev(`document.querySelector('button')?.innerText`));
console.log("window.snap:", await ev(`typeof window.snap`));
console.log("snap.=== :", await ev(`window.snap ? Object.keys(window.snap).join(',') : 'n/a'`));

// klik tombol bayar
await ev(`document.querySelector('button')?.click()`);
await sleep(9000);

console.log("--- iframe ---");
console.log(
  await ev(`JSON.stringify([...document.querySelectorAll('iframe')].map(f=>({src:f.src, w:f.clientWidth, h:f.clientHeight})))`)
);
console.log("--- dialogs/overlay divs ---");
console.log(
  await ev(`JSON.stringify([...document.querySelectorAll('div')].filter(d=>/snap|midtrans/i.test(d.className||'')).map(d=>({c:d.className.slice(0,80),h:d.clientHeight})).slice(0,15))`)
);

console.log("--- console ---");
console.log(logs.slice(0, 40).join("\n"));
console.log("--- failed requests ---");
console.log([...new Set(failed)].slice(0, 30).join("\n"));

const r = await send("Page.captureScreenshot", { format: "png" });
writeFileSync("/tmp/snap-debug.png", Buffer.from(r.data, "base64"));

ws.close();
chrome.kill();
