# HomeGlo Add-on for HomeAssistant


```aiignore
docker run --rm -it --name builder --privileged \                   
  -v "$(pwd)":/data \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  ghcr.io/home-assistant/amd64-builder \
  -t /data \
  --test \
  --aarch64 \
  -i homeglo-aarch64 \
  -d local
  
  
docker run --rm -it \
-e HA_TOKEN= \
-e HA_WEBSOCKET_URL=ws://homeassistant.local:8123/api/websocket \
-p 80:80 \
local/homeglo-aarch64:latest
```