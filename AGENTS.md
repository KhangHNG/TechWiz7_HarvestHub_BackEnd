# AGENTS.md

## Strict Language Matching Rule (Real-time Mirroring)
- **Mandatory Language Detection:** Always detect the language of the user's latest input and respond in that exact language in real-time.
- **Immediate Language Switching:**
  - If the user asks in Vietnamese -> Respond 100% in Vietnamese (Tiếng Việt).
  - If the user asks in English -> Respond 100% in English.
  - If the user switches languages between consecutive turns, switch immediately without lagging or carrying over the previous language.
- **Ignore Code / Log Bias:** Even if terminal logs, code snippets, or error traces in the prompt are in English, the explanation and response must strictly match the language written by the user.
