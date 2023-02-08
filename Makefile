build:
	docker build -t image . && docker run -it --rm --name my-running-app -v ${PWD}:/usr/src/app image /usr/local/bin/composer install

exec:
	docker run -it --rm --name my-running-app -v ${PWD}:/usr/src/app image php index.php "$(voice)" "$(text)"
