
from pydantic_settings import BaseSettings 
class Settings(BaseSettings):
    env: str = "development"
    secret_key: str
    algorithm: str 
    postgres_host: str
    postgres_port: int
    postgres_user: str
    postgres_password: str
    postgres_db: str

    redis_url: str
    llm_endpoint: str

    api_host: str = "0.0.0.0"
    api_port: int = 8000

    class Config:
        env_file = ".env"

settings = Settings()
