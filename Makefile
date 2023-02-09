IMAGE_NAME := fakeyouwrapper
CONTAINER_NAME := my-fakeyouwrapper

build:
	docker build -t $(IMAGE_NAME) . && docker run -it --rm --name my-fakeyouwrapper -v ${PWD}:/usr/src/app $(IMAGE_NAME) /usr/local/bin/composer install

exec:
	docker run -it --rm --name $(CONTAINER_NAME) -v ${PWD}:/usr/src/app $(IMAGE_NAME) php index.php "$(voice)" "$(text)"
