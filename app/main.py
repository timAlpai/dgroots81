from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import text

from app.core import config, redis, llm_client
from app.core.database import get_db
from app.api import auth, users, game_sessions, characters, actions, scenarios, scenes

app = FastAPI(
    title="RPG-IA API",
    description="API pour un système de jeu de rôle en ligne multi-joueurs avec IA comme maître de jeu",
    version="1.0.0",
    root_path="/api/v1"
)

# Configuration CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # À remplacer par les domaines autorisés en production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Routes de base
@app.get("/", tags=["status"])
def ping():
    return {"status": "ok", "message": "Bienvenue sur l'API RPG-IA"}

@app.get("/health", tags=["status"])
async def health_check(db=Depends(get_db)):
    try:
        pong = await redis.redis_client.ping()
        llm_resp = await llm_client.call_llm([
            {"role": "user", "content": "ping"}
        ], max_tokens=10)
        result = await db.execute(text("SELECT 'PostgreSQL OK!'"))
        msg = result.scalar()
        return {
            "status": "ok",
            "redis": pong,
            "llm": llm_resp["choices"][0]["message"]["content"],
            "postgresql": msg
        }
    except Exception as e:
        return {"status": "error", "error": str(e)}

# Inclusion des routes API
app.include_router(auth.router, prefix="/api")
app.include_router(users.router, prefix="/api")
app.include_router(game_sessions.router, prefix="/api")
app.include_router(characters.router, prefix="/api")
app.include_router(actions.router, prefix="/api")
app.include_router(scenarios.router, prefix="/api")
app.include_router(scenes.router, prefix="/api")

