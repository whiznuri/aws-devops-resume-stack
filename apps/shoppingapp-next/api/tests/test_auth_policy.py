"""เทสกติกา Business — อ่านไฟล์นี้แล้วรู้กติกาการล็อกอินทั้งหมด (เทส = สเปคที่รันได้)"""

from datetime import timedelta

from app.auth.policy import SESSION_POLICIES, can_access


def test_admin_is_god():
    assert can_access("admin", ("staff",))
    assert can_access("admin", ("admin",))


def test_staff_cannot_enter_admin_area():
    assert can_access("staff", ("staff",))
    assert not can_access("staff", ("admin",))


def test_customer_cannot_enter_backoffice():
    assert not can_access("customer", ("staff",))


def test_staff_and_admin_session_rules():
    for role in ("staff", "admin"):
        assert SESSION_POLICIES[role].idle == timedelta(minutes=30)
        assert SESSION_POLICIES[role].absolute == timedelta(hours=8)


def test_customer_session_rules():
    assert SESSION_POLICIES["customer"].idle is None
    assert SESSION_POLICIES["customer"].absolute == timedelta(days=7)
