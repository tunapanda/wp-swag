copy-deps:
	rm -rf ext/wpcrud
	rsync -r --exclude .git submodule/wpcrud/ ext/wpcrud

	rm -rf ext/wprecord
	rsync -r --exclude .git submodule/wprecord/ ext/wprecord

link-deps:
	rm -rf ext/wpcrud
	cd ext; ln -s ../submodule/wpcrud wpcrud

	rm -rf ext/wprecord
	cd ext; ln -s ../submodule/wprecord wprecord
