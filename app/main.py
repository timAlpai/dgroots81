from fastapi import FastAPI
from app.core import config, redis, llm_client
from app.core.database import get_db
from fastapi import Depends
from sqlalchemy import text


app = FastAPI()

@app.get("/")
def ping():
    return {"status": "ok"}

@app.get("/health")
async def health_check(db=Depends(get_db)):
    try:
        pong = await redis.redis_client.ping()
        llm_resp = await llm_client.call_llm([
            {"role": "user", "content": "ping"}
        ], max_tokens=10)
        result = await db.execute(text("SELECT 'PostgreSQL OK!'"))
        msg = result.scalar()
        return {
            "redis": pong,
            "llm": llm_resp["choices"][0]["message"]["content"],
            "postgresql": msg
        }
    except Exception as e:
        return {"error": str(e)}


@app.get("/pg")
async def test_postgres(db=Depends(get_db)):
    result = await db.execute(text("SELECT 'PostgreSQL OK!'"))
    msg = result.scalar()
    return {"postgresql": msg}
