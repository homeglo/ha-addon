#!/usr/bin/with-contenv bashio
# 00-ha-connection.sh – prepare HA_* vars for downstream processes
# Runs in /etc/cont-init.d (root, before services)

set -e          # fail on command errors
# DO NOT use `set -u` (nounset); s6-overlay containers often rely on unset vars

log() { echo "[00-ha-connection] $*"; }

# ---------------------------------------------------------------
# 1. Detect mode
#    - If SUPERVISOR_TOKEN is present → ADDON
#    - Else → STANDALONE
# ---------------------------------------------------------------
if [[ -n "${SUPERVISOR_TOKEN:-}" ]]; then
    MODE="ADDON"
    log "Addon mode (SUPERVISOR_TOKEN present)"
    HA_TOKEN="${HA_TOKEN:-$SUPERVISOR_TOKEN}"
    HA_WEBSOCKET_URL="${HA_WEBSOCKET_URL:-ws://supervisor/core/api/websocket}"
    HA_REST_URL="${HA_REST_URL:-http://supervisor/core/api}"
else
    MODE="STANDALONE"
    log "Standalone mode (no supervisor token)"
    HA_WEBSOCKET_URL="${HA_WEBSOCKET_URL:-ws://homeassistant.local:8123/api/websocket}"
    HA_REST_URL="${HA_REST_URL:-http://homeassistant.local:8123/api}"
    [[ -n "${HA_TOKEN:-}" ]] || log "WARNING: HA_TOKEN not provided; pass -e HA_TOKEN=<token>"
fi

# ---------------------------------------------------------------
# 2. Persist vars for all later services
#    Only write a file if the value is non-empty to avoid nounset errors
# ---------------------------------------------------------------
persist() {
    local name="$1" value="$2"
    [[ -n "$value" ]] && echo -n "$value" > "/var/run/s6/container_environment/$name"
}

persist HA_TOKEN         "${HA_TOKEN:-}"
persist HA_WEBSOCKET_URL "${HA_WEBSOCKET_URL:-}"
persist HA_REST_URL      "${HA_REST_URL:-}"

# ---------------------------------------------------------------
# 3. Log summary
# ---------------------------------------------------------------
log "Environment ready – MODE=$MODE"
log "  HA_TOKEN set:     $([[ -n ${HA_TOKEN:-} ]] && echo yes || echo no)"
log "  HA_WEBSOCKET_URL: ${HA_WEBSOCKET_URL:-<unset>}"
log "  HA_REST_URL:      ${HA_REST_URL:-<unset>}"