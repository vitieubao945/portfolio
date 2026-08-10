# Development frontend runtime: Node + Yarn (via Corepack) + Vite + Vue 3.
# The Sage theme is bind-mounted in docker-compose.yml; dependencies are
# installed into an anonymous volume so the bind mount never hides them.
FROM node:22-alpine

RUN corepack enable

WORKDIR /app

CMD ["yarn", "dev"]
