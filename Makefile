copy-deps:
	rm -rf ext/wpcrud
	rsync -r --exclude .git submodule/wpcrud/ ext/wpcrud

	rm -rf ext/wprecord
	rsync -r --exclude .git submodule/wprecord/ ext/wprecord

	rm -rf ext/meta-box
	rsync -r --exclude .git submodule/meta-box/ ext/meta-box

link-deps:
	rm -rf ext/wpcrud
	cd ext; ln -s ../submodule/wpcrud wpcrud

	rm -rf ext/wprecord
	cd ext; ln -s ../submodule/wprecord wprecord

	rm -rf ext/meta-box
	cd ext; ln -s ../submodule/meta-box meta-box
