
from pydantic_settings import BaseSettings 
class Settings(BaseSettings):
    env: str = "development"

    # PostgreSQL
    postgres_host: str
    postgres_port: int
    postgres_user: str
    postgres_password: str
    postgres_db: str

    # Redis
    redis_url: str

    # LLM
    llm_endpoint: str

    # API
    api_host: str = "0.0.0.0"
    api_port: int = 8000
    # JWT
    secret_key: str 
    algorithm: str = "HS256" 
    class Config:
        env_file = ".env"

settings = Settings()
