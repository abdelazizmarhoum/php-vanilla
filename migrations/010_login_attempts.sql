CREATE TABLE login_attempts (
    id BIGSERIAL PRIMARY KEY,
    email CITEXT NOT NULL,
    ip INET,
    success BOOLEAN NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX login_attempts_email_time_idx
    ON login_attempts (email, created_at DESC);
