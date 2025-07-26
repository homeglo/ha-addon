#!/usr/bin/with-contenv bashio
# 00-ha-connection.sh – prepare HA_* vars for downstream processes
# Runs in /etc/cont-init.d (executed as root before services start)

set -e
log(){ echo "[00-ha-connection] $*"; }

# ---------------------------------------------------------------
# Detect Supervisor token → “ADDON” mode, else “STANDALONE” mode
# ---------------------------------------------------------------
SUPERVISOR_TOKEN_VALUE="$(bashio::var.json SUPERVISOR_TOKEN 2>/dev/null || true)"

if [[ -n "$SUPERVISOR_TOKEN_VALUE" ]]; then
    MODE="ADDON"
    log "Addon mode (supervisor token present)"
    : "${HA_TOKEN:=$SUPERVISOR_TOKEN_VALUE}"
    : "${HA_WEBSOCKET_URL:=ws://supervisor/core/api/websocket}"
    : "${HA_REST_URL:=http://supervisor/core/api}"
else
    MODE="STANDALONE"
    log "Standalone mode (no supervisor token)"
    : "${HA_WEBSOCKET_URL:=ws://homeassistant.local:8123/api/websocket}"
    : "${HA_REST_URL:=http://homeassistant.local:8123/api}"
    if [[ -z "$HA_TOKEN" ]]; then
        log "WARNING: HA_TOKEN not provided; pass -e HA_TOKEN=<token> when you docker-run"
    fi
fi

# ---------------------------------------------------------------
# Persist the final values so every later process (php-fpm, cron,
# etc.) inherits them.  Only needed if we *set* or *override* them.
# ---------------------------------------------------------------
persist() { echo -n "$2" > "/var/run/s6/container_environment/$1"; }

persist HA_TOKEN         "${HA_TOKEN}"
persist HA_WEBSOCKET_URL "${HA_WEBSOCKET_URL}"
persist HA_REST_URL      "${HA_REST_URL}"

log "Environment ready: MODE=$MODE"
log "  HA_TOKEN set:     $([[ -n $HA_TOKEN ]] && echo yes || echo no)"
log "  HA_WEBSOCKET_URL: ${HA_WEBSOCKET_URL}"
log "  HA_REST_URL:      ${HA_REST_URL}"