---
paths:
  - 'app/Mcp/**'
---

# Mcp

## MCP identity and scope boundary
MCP tools must derive the user from the Passport-authenticated request; never accept a user ID or email. The public MCP route requires auth:api plus the Laravel MCP mcp:use scope, and writes must use the same domain action and limits as the web flow.
