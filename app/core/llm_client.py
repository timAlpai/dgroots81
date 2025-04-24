import httpx
from app.core.config import settings

async def call_llm(messages: list, max_tokens=100, temperature=0.7):
    payload = {
        "model": "shuyuej/Mistral-Nemo-Instruct-2407-GPTQ-INT8",
        "messages": messages,
        "temperature": temperature,
        "max_tokens": max_tokens
    }
    async with httpx.AsyncClient() as client:
        resp = await client.post(f"{settings.llm_endpoint}/v1/chat/completions", json=payload)
        resp.raise_for_status()
        return resp.json()
