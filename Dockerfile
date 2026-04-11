# ─────────────────────────────────────────────────────────────────────────────
# Stage 1 — Builder
# Pins Node.js to exactly 22.12.0 so rolldown's native bindings are installed
# for the correct runtime, bypassing Railpack/Nixpacks version detection.
# ─────────────────────────────────────────────────────────────────────────────
FROM node:22.12.0-alpine AS builder

WORKDIR /app

# Copy the entire monorepo so workspace resolution works correctly
COPY . .

# Install all dependencies (resolves workspace packages and installs
# rolldown native bindings compiled for Node 22.12.0)
RUN npm install

# Build the frontend PWA with Vite
RUN npm run build --workspace=frontend

# ─────────────────────────────────────────────────────────────────────────────
# Stage 2 — Runner
# Minimal image that only ships the compiled static assets + a static server.
# ─────────────────────────────────────────────────────────────────────────────
FROM node:22.12.0-alpine AS runner

WORKDIR /app

# Install `serve` globally — lightweight static file server
RUN npm install -g serve

# Copy only the built frontend assets from the builder stage
COPY --from=builder /app/frontend/dist ./dist

EXPOSE 3000

# Railway injects $PORT at runtime; fall back to 3000 for local runs
CMD ["sh", "-c", "serve -s dist -l ${PORT:-3000}"]
