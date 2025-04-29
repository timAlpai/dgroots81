import httpx
from app.core.config import settings

async def call_llm(messages: list, max_tokens=500, temperature=0.7):
    payload = {
        "model": "shuyuej/Mistral-Nemo-Instruct-2407-GPTQ-INT8",
        "messages": messages,
        "temperature": temperature,
        "max_tokens": max_tokens
    }
    async with httpx.AsyncClient() as client:
        response = await client.post(f"{settings.llm_endpoint}/chat/completions", json=payload)
        response.raise_for_status()
        return response.json()
