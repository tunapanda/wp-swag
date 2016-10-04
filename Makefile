copy-deps:
	rm -rf ext/wpcrud
	rsync -r --exclude .git submodule/wpcrud/ ext/wpcrud

link-deps:
	rm -rf ext/wpcrud
	cd ext; ln -s ../submodule/wpcrud wpcrud
