CREATE TABLE invitations (
    id BIGSERIAL PRIMARY KEY,
    workspace_id BIGINT NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
    email CITEXT NOT NULL,
    role workspace_role NOT NULL DEFAULT 'member',
    token_hash TEXT NOT NULL,
    invited_by BIGINT NOT NULL REFERENCES users(id),
    expires_at TIMESTAMPTZ NOT NULL,
    accepted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX invitations_token_idx ON invitations (token_hash);
CREATE INDEX invitations_workspace_idx ON invitations (workspace_id);
CREATE UNIQUE INDEX invitations_pending_unique
    ON invitations (workspace_id, email)
    WHERE accepted_at IS NULL;
