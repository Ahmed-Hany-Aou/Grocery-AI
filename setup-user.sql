-- Create Laravel user and grant privileges
CREATE USER laravel WITH PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE groceryai TO laravel;
ALTER DATABASE groceryai OWNER TO laravel;

-- Connect to groceryai database
\c groceryai

-- Grant schema privileges
GRANT ALL ON SCHEMA public TO laravel;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO laravel;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO laravel;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO laravel;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO laravel;
