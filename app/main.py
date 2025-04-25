# Standard library imports
from typing import Any

# Third-party imports
from fastapi import FastAPI, Depends
from sqlalchemy import text

# Local application imports
from app.core import redis, llm_client
from app.core.database import get_db
from app.routes import auth, admin, me, admin_credits, joueur, personnage, jeu, public

app = FastAPI()

def include_public_routes(app: FastAPI) -> None:
    @app.get("/")
    def ping():
        return {"status": "ok"}

    @app.get("/health")
    async def health_check(db=Depends(get_db)):
        try:
            pong = await redis.redis_client.ping()
            result = await db.execute(text("SELECT 'PostgreSQL OK!'"))
            msg = result.scalar()
            llm_resp = await llm_client.call_llm([
                {"role": "user", "content": "c'est un ping, un health status d'un saas répond en français stp"}
            ], max_tokens=500)
            return {
                "redis": pong,
                "postgresql": msg,
                "llm": llm_resp["choices"][0]["message"]["content"]
            }
        except Exception as e:
            return {"error": str(e)}

    @app.get("/pg")
    async def test_postgres(db=Depends(get_db)):
        result = await db.execute(text("SELECT 'PostgreSQL OK!'"))
        msg = result.scalar()
        return {"postgresql": msg}

def include_api_routes(app: FastAPI) -> None:
    app.include_router(auth.router)
    app.include_router(admin.router)
    app.include_router(me.router)
    app.include_router(admin_credits.router)
    app.include_router(joueur.router)
    app.include_router(personnage.router)
    app.include_router(jeu.router)

include_public_routes(app)
include_api_routes(app)
