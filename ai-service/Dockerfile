# Lightweight Python base image
FROM python:3.11-slim

# Set environment variables
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
        PORT=7777

        # Set working directory
        WORKDIR /app

        # Copy requirements and install
        COPY requirements.txt .
        RUN pip install --no-cache-dir -r requirements.txt && \
            python -m playwright install --with-deps chromium

            # Copy the application code
            COPY . .

            # Expose the Cloud Run port
            EXPOSE 7777

            # Start the server
            CMD ["sh", "-c", "uvicorn main:app --host 0.0.0.0 --port ${PORT}"]
