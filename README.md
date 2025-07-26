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
```