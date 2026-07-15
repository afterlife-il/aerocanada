import { createHash, randomBytes, timingSafeEqual } from "node:crypto";

export type OAuthProviderName = "google" | "microsoft" | "apple" | "linkedin";

interface ProviderDefinition { displayName: string; authorizationEndpoint: string; tokenEndpoint: string; scopes: string[]; oidc: boolean; }

const definitions: Record<OAuthProviderName, ProviderDefinition> = {
  google: { displayName: "Google", authorizationEndpoint: "https://accounts.google.com/o/oauth2/v2/auth", tokenEndpoint: "https://oauth2.googleapis.com/token", scopes: ["openid", "email", "profile"], oidc: true },
  microsoft: { displayName: "Microsoft", authorizationEndpoint: "https://login.microsoftonline.com/common/oauth2/v2.0/authorize", tokenEndpoint: "https://login.microsoftonline.com/common/oauth2/v2.0/token", scopes: ["openid", "email", "profile"], oidc: true },
  apple: { displayName: "Apple", authorizationEndpoint: "https://appleid.apple.com/auth/authorize", tokenEndpoint: "https://appleid.apple.com/auth/token", scopes: ["name", "email"], oidc: true },
  linkedin: { displayName: "LinkedIn", authorizationEndpoint: "https://www.linkedin.com/oauth/v2/authorization", tokenEndpoint: "https://www.linkedin.com/oauth/v2/accessToken", scopes: ["openid", "profile", "email"], oidc: true }
};

export interface OAuthProviderStatus { provider: OAuthProviderName; displayName: string; configured: boolean; message: string; }

export function oauthProviderStatuses(env: NodeJS.ProcessEnv = process.env): OAuthProviderStatus[] {
  return (Object.keys(definitions) as OAuthProviderName[]).map((provider) => {
    const prefix = `OAUTH_${provider.toUpperCase()}`;
    const configured = Boolean(env[`${prefix}_CLIENT_ID`] && env[`${prefix}_CLIENT_SECRET`] && env[`${prefix}_REDIRECT_URI`]);
    return { provider, displayName: definitions[provider].displayName, configured, message: configured ? "Configured; callback acceptance still required." : "Not configured for this staging environment" };
  });
}

export interface OAuthAuthorizationRequest { state: string; nonce: string; codeVerifier: string; authorizationUrl: string; }

export function createOAuthAuthorizationRequest(provider: OAuthProviderName, clientId: string, redirectUri: string): OAuthAuthorizationRequest {
  const definition = definitions[provider]; const state = randomBytes(32).toString("base64url"); const nonce = randomBytes(32).toString("base64url"); const codeVerifier = randomBytes(48).toString("base64url");
  const challenge = createHash("sha256").update(codeVerifier).digest("base64url");
  const query = new URLSearchParams({ response_type: "code", client_id: clientId, redirect_uri: redirectUri, scope: definition.scopes.join(" "), state, code_challenge: challenge, code_challenge_method: "S256" });
  if (definition.oidc) query.set("nonce", nonce);
  return { state, nonce, codeVerifier, authorizationUrl: `${definition.authorizationEndpoint}?${query}` };
}

export function validateOAuthValue(expected: string, actual: string): boolean {
  const left = Buffer.from(createHash("sha256").update(expected).digest()); const right = Buffer.from(createHash("sha256").update(actual).digest());
  return timingSafeEqual(left, right);
}

export function providerTokenEndpoint(provider: OAuthProviderName): string { return definitions[provider].tokenEndpoint; }
