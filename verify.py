#!/usr/bin/env python3
"""verify.py -- CI gate. Ujrafuttatja a php referenciat a kanonikus korpuszon, es exit 0 IFF minden
eset egyezik ES a korpusz nem degeneralt. A kimenet a publish-kapu (compute-oracle) szerzodese: utolso
sor "ZOLD". Nincs recall/FP: konformancia-korpuszon az hamis metrika volna.
FUGGOSEG: php futtato a PATH-on (a bundle kodja nem python). Ha nincs, a gate NEM-MERT-tel bukik --
az nem "eltores", hanem "nem tudtam megnezni", es a kimenet kimondja."""
import json
import os
import shutil
import subprocess
import sys

sys.dont_write_bytecode = True
BASE = os.path.dirname(os.path.abspath(__file__))
RUNNER = ['php']


def main():
    if not shutil.which(RUNNER[0]):
        print("NEM-MERT: nincs %s futtato a PATH-on -- ez nem eltores, hanem nem tudtam megnezni" % RUNNER[0])
        return 2
    probes = [json.loads(l) for l in open(os.path.join(BASE, "probes", "probes.jsonl"), encoding="utf-8")
              if l.strip()]
    if len({json.dumps(p["expect"], sort_keys=True) for p in probes}) < 2:
        print("DEGENERALT korpusz -- PIROS")
        return 1
    src = open(os.path.join(BASE, "oracle", "probe.php"), encoding="utf-8").read()
    inputs = [p["input"] for p in probes]
    r = subprocess.run(RUNNER + ["probe.php"], input=None, capture_output=True, text=True, timeout=60,
                       cwd=os.path.join(BASE, "oracle"))
    if r.returncode != 0:
        print("futas-hiba: %s" % (r.stderr or "")[-120:])
        return 1
    line = [l for l in r.stdout.strip().splitlines() if l.strip().startswith("{")][-1]
    out = json.loads(line)["out"]
    agree = disagree = 0
    for p, o in zip(probes, out):
        if o.get("ok") and o.get("v") == p["expect"]:
            agree += 1
        else:
            disagree += 1
            print("  ELTERES input=%r" % (p["input"],))
    print("markdown conformance oracle (php): probes=%d | agree=%d | disagree=%d"
          % (len(probes), agree, disagree))
    if disagree:
        return 1
    print("ZOLD")
    return 0


if __name__ == "__main__":
    sys.exit(main())
