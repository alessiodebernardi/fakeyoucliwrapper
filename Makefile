build:
	docker build -t fakeyouwrapper . && docker run -it --rm --name my-fakeyouwrapper -v ${PWD}:/usr/src/app image /usr/local/bin/composer install

exec:
	docker run -it --rm --name my-fakeyouwrapper -v ${PWD}:/usr/src/app fakeyouwrapper php index.php "$(voice)" "$(text)"
