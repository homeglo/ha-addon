docker build -t homeglo-php-dev .

docker run --rm -it \
  -p 8000:80 \
  -v ~/.composer-docker/cache:/root/.composer/cache:delegated \
  -v "$PWD":/app:delegated \
  --name homeglo-php \
  homeglo-php-dev