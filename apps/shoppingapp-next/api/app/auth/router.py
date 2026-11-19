from datetime import UTC, datetime
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, Response, status
from sqlalchemy.orm import Session

from app.auth import service
from app.auth.deps import Claims, get_claims
from app.auth.schemas import LoginIn, RefreshIn, RegisterIn, TokenPair, UserOut
from app.db import get_session
from app.models import User

router = APIRouter(prefix="/auth", tags=["auth"])
SessionDep = Annotated[Session, Depends(get_session)]


@router.post("/register", status_code=status.HTTP_201_CREATED)
def register(body: RegisterIn, session: SessionDep) -> UserOut:
    # สมัครเองได้แค่ customer · staff/admin ต้องให้ admin สร้าง (หรือสคริปต์ create_user)
    try:
        user = service.create_user(session, body.email, body.password, role="customer")
    except service.EmailTaken:
        raise HTTPException(status.HTTP_409_CONFLICT, "email นี้มีผู้ใช้แล้ว") from None
    return UserOut.model_validate(user)


@router.post("/login")
def login(body: LoginIn, session: SessionDep) -> TokenPair:
    try:
        return service.login(session, body.email, body.password, now=datetime.now(UTC))
    except service.AuthError as exc:
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, str(exc)) from None


@router.post("/refresh")
def refresh(body: RefreshIn, session: SessionDep) -> TokenPair:
    try:
        return service.refresh(session, body.refresh_token, now=datetime.now(UTC))
    except service.AuthError as exc:
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, str(exc)) from None


@router.post("/logout", status_code=status.HTTP_204_NO_CONTENT)
def logout(body: RefreshIn, session: SessionDep) -> Response:
    service.logout(session, body.refresh_token, now=datetime.now(UTC))
    return Response(status_code=status.HTTP_204_NO_CONTENT)


@router.get("/me")
def me(claims: Annotated[Claims, Depends(get_claims)], session: SessionDep) -> UserOut:
    user = session.get(User, claims.user_id)
    if user is None or not user.is_active:
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, "บัญชีนี้ใช้งานไม่ได้")
    return UserOut.model_validate(user)
