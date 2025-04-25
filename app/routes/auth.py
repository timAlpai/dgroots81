from fastapi import APIRouter, HTTPException, status, Depends
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.database import get_db
from app.schemas.user import UserCreate, UserOut
from app.schemas.token import Token
from app.services.auth_service import AuthService # Import du service

router = APIRouter(prefix="/auth", tags=["Auth"])

# Dépendance pour obtenir l'AuthService
async def get_auth_service(db: AsyncSession = Depends(get_db)) -> AuthService:
    return AuthService(db)

@router.post("/register", response_model=UserOut)
async def register(
    user_data: UserCreate,
    auth_service: AuthService = Depends(get_auth_service) # Injection du service
):
    new_user = await auth_service.create_user(user_data)
    if new_user is None:
        raise HTTPException(status_code=400, detail="Email already registered")

    return new_user


@router.post("/login", response_model=Token)
async def login(
    form_data: UserCreate, # Renommé pour clarté, bien que le schéma soit UserCreate
    auth_service: AuthService = Depends(get_auth_service) # Injection du service
):
    user = await auth_service.authenticate_user(form_data.email, form_data.password)
    if not user:
        raise HTTPException(status_code=401, detail="Invalid credentials")

    token = auth_service.create_access_token_for_user(user.id)
    return {"access_token": token, "token_type": "bearer"}