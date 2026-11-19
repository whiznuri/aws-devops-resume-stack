from datetime import datetime

from pydantic import BaseModel, ConfigDict, EmailStr, Field


class RegisterIn(BaseModel):
    email: EmailStr
    password: str = Field(min_length=8, max_length=128)


class LoginIn(BaseModel):
    email: EmailStr
    password: str


class RefreshIn(BaseModel):
    refresh_token: str


class TokenPair(BaseModel):
    access_token: str
    access_expires_at: datetime
    refresh_token: str
    session_expires_at: datetime
    token_type: str = "bearer"


class UserOut(BaseModel):
    """ใบที่ส่งให้คนนอกดู — ไม่มี password_hash (ดู lessons/orm.md: schema ≠ model)"""

    model_config = ConfigDict(from_attributes=True)

    id: int
    email: EmailStr
    role: str
    is_active: bool
