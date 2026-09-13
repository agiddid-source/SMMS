import urllib.request
import urllib.parse
import http.cookiejar
import json
import re
import sys

BASE = "http://localhost:8897"
DATA_DIR = "/tmp/gh-php-test/data"
results = []


def check(label, cond):
    results.append((label, bool(cond)))


cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))


def get(path):
    with opener.open(BASE + path) as resp:
        return resp.status, resp.read().decode("utf-8")


def post(path, fields):
    data = urllib.parse.urlencode(fields, doseq=True).encode("utf-8")
    req = urllib.request.Request(BASE + path, data=data, method="POST")
    with opener.open(req) as resp:
        return resp.status, resp.read().decode("utf-8")


# ---- Regression: pre-existing pages still work after the index.php edit ----

status, body = get("/index.php?p=dashboard")
check("dashboard (pre-existing) still loads fine", status == 200 and "Finance overview" in body)

status, body = get("/index.php?p=student-profile&student=sodiq-adeyemi")
check("student-profile (pre-existing) still loads fine", status == 200)

status, body = get("/index.php?p=coming-soon&module=Expenses")
check("coming-soon (pre-existing) still loads fine", status == 200 and "Expenses is coming soon" in body)

status, body = get("/index.php?p=does-not-exist")
check("unknown route still falls back to 404", status == 200 and "Page not found" in body)

# ---- Classes module ----

status, body = get("/index.php?p=classes")
check("classes page loads (200)", status == 200)
check(
    "classes nav link marked active",
    re.search(r'ght-nav-sub-link--active"[^>]*href="index\.php\?p=classes"', body) is not None
    or re.search(r'ght-nav-sub-link\s+ght-nav-link--active"\s+href="index\.php\?p=classes"', body) is not None
)
check("classes page renders seeded classes", "JSS 3" in body and "SS 3" in body)
check("classes page groups by section", "Primary" in body and "Secondary" in body)
check("classes page reuses the shared chart-view-link filter pattern", "ght-chart-view-link" in body)

status, body = get("/index.php?p=classes&search=SS+3")
check("classes search matches both SS 3 and JSS 3 (substring)", "JSS 3" in body and "SS 3" in body)
check("classes search excludes non-matching classes", "Year 1" not in body)

status, body = get("/index.php?p=classes&section=Primary")
check("classes section filter narrows to Primary only", "Year 1" in body and "JSS 3" not in body)

status, body = post("/index.php?p=classes", {"action": "add", "name": "Year 6", "section": "Primary"})
check("POST add class redirects and follows through (200)", status == 200)
status, body = get("/index.php?p=classes")
check("newly added class appears in the list", "Year 6" in body)

with open(f"{DATA_DIR}/classes.json") as f:
    classes_data = json.load(f)
new_class = next(c for c in classes_data if c["name"] == "Year 6")

status, body = post("/index.php?p=classes", {"action": "edit", "id": new_class["id"], "name": "Year 6 Renamed", "section": "Primary"})
status, body = get("/index.php?p=classes")
check("edited class shows its new name", "Year 6 Renamed" in body)

status, body = post("/index.php?p=classes", {"action": "archive", "id": new_class["id"]})
status, body = get("/index.php?p=classes")
check("archived class no longer appears in the active list", "Year 6 Renamed" not in body)

with open(f"{DATA_DIR}/classes.json") as f:
    classes_after = json.load(f)
archived = next(c for c in classes_after if c["id"] == new_class["id"])
check("archived class's status persisted to disk", archived["status"] == "archived")

# ---- Fees module ----

status, body = get("/index.php?p=fee-setup")
check("fees page loads (200)", status == 200)
check("fees page renders seeded fees", "Tuition" in body and "WAEC Fee" in body)
check("fees page resolves assigned class names", "Year 1" in body and "Year 2" in body)
check("fees page formats amount as Naira", "&#8358;87,000" in body or "₦87,000" in body)

status, body = get("/index.php?p=fee-setup&type=WAEC")
check("fees type filter narrows to WAEC only", "WAEC Fee" in body and "Examination Fee" not in body)

status, body = get("/index.php?p=fee-setup&search=Tuition")
check("fees search narrows to matching fee only", "Tuition" in body and "WAEC Fee" not in body)

status, body = post("/index.php?p=fee-setup", {"action": "add_type", "name": "Excursion"})
with open(f"{DATA_DIR}/fee-types.json") as f:
    types_data = json.load(f)
check("new fee type persisted to disk", any(t["name"] == "Excursion" for t in types_data))
status, body = get("/index.php?p=fee-setup")
check("new fee type appears in the type filter dropdown", "Excursion" in body)

with open(f"{DATA_DIR}/classes.json") as f:
    classes_data = json.load(f)
class_ids = [c["id"] for c in classes_data if c["name"] in ("Nursery 1", "Nursery 2")]
status, body = post("/index.php?p=fee-setup", {
    "action": "add",
    "name": "Uniform Fee",
    "type": "Uniform",
    "amount": "15000",
    "academicSession": "2026/2027",
    "term": "First Term",
    "dueDate": "2026-10-01",
    "description": "",
    "classIds[]": class_ids,
})
status, body = get("/index.php?p=fee-setup")
check("newly added fee appears in the list", "Uniform Fee" in body)
check("newly added fee shows both assigned class names", "Nursery 1" in body and "Nursery 2" in body)

with open(f"{DATA_DIR}/fees.json") as f:
    fees_data = json.load(f)
new_fee = next(f for f in fees_data if f["name"] == "Uniform Fee")
check("new fee's amount persisted correctly", new_fee["amount"] == 15000)
check("new fee's assignedClasses persisted correctly", set(new_fee["assignedClasses"]) == set(class_ids))

status, body = post("/index.php?p=fee-setup", {
    "action": "edit", "id": new_fee["id"], "name": "Uniform & Kit Fee", "type": "Uniform",
    "amount": "17500", "academicSession": "2026/2027", "term": "First Term",
    "dueDate": "2026-10-01", "description": "", "classIds[]": class_ids,
})
status, body = get("/index.php?p=fee-setup")
check("edited fee shows new name (HTML-escaped ampersand)", "Uniform &amp; Kit Fee" in body)

status, body = post("/index.php?p=fee-setup", {"action": "deactivate", "id": new_fee["id"]})
status, body = get("/index.php?p=fee-setup")
check("deactivated fee no longer appears in the active list", "Uniform &amp; Kit Fee" not in body)

with open(f"{DATA_DIR}/fees.json") as f:
    fees_after = json.load(f)
deactivated = next(f for f in fees_after if f["id"] == new_fee["id"])
check("deactivated fee's status persisted to disk", deactivated["status"] == "inactive")

# ---- Report ----
failed = 0
for label, passed in results:
    print(("PASS" if passed else "FAIL") + " — " + label)
    if not passed:
        failed += 1
print(f"\n{len(results) - failed}/{len(results)} checks passed")
sys.exit(1 if failed else 0)
