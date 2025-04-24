from datetime import datetime, timedelta
from jose import JWTError, jwt
from passlib.context import CryptContext
from app.core.config import settings

SECRET_KEY = settings.secret_key  # <-- utilise la valeur depuis le .env
ALGORITHM = settings.algorithm     # idem
ACCESS_TOKEN_EXPIRE_MINUTES = 60 * 24  # 24h
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

def verify_password(plain_password, hashed_password):
    return pwd_context.verify(plain_password, hashed_password)

def hash_password(password):
    return pwd_context.hash(password)

def create_access_token(data: dict, expires_delta: timedelta | None = None):
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=15))
    to_encode.update({"exp": expire})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)

def decode_token(token: str):
    return jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])

def create_tokens(data: dict):
    access_token = create_access_token(data, timedelta(minutes=15))
    refresh_token = create_access_token(data, timedelta(days=30))  # ou autre durée
    return access_token, refresh_token

__all__ = [
    "hash_password",
    "verify_password",
    "create_access_token",
    "create_tokens",
    "decode_token",
]