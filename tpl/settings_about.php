<p>
	Welcome to Swag!
</p>

<p>
	The gamified, self-paced learning environment from Tunapanda! 
</p>

<p>
	There are a few things you might want to set up:

	<ol>
		<li>
			Install and activate the 
			<a href="<?php echo admin_url("plugins.php?page=tgmpa-install-plugins"); ?>"
				>required plugins</a>.
		</li>
		<li>
			Set up a remote source to sync learning content. Depending on your use case there are a 
			few different options.
			<ul>
				<li>
					<p>
						If you want the peer reviewed and released swagpaths, use content from
						<a href="<?php 
									$source=urlencode("http://learning.tunapanda.org/");
									echo admin_url(
										"options-general.php?page=rs_main&tab=connection&".
										"rs_remote_site_url=$source"
									); 
								?>">learning.tunapanda.org</a>.
					</p>
				</li>
				<li>
					<p>
						If you want content from the bleeding edge and want to activly participate in
						swagpath development, use 
						<a href="<?php 
									$source=urlencode("http://swagstaging.tunapanda.org/");
									echo admin_url(
										"options-general.php?page=rs_main&tab=connection&".
										"rs_remote_site_url=$source"
									);
								?>">swagstaging.tunapanda.org</a>.
						You will also have to set the access key in order to upload new content.
					</p>
				</li>
			</ul>
		</li>
		<li>
			After you set up a remote connection for syncing, use the remote sync plugin in order to 
			<a href="<?php
					echo admin_url("options.php?page=rs_sync_preview")
				?>">download</a>
			content to your local server.
		</li>
		<li>
			If you want a Tunapanda look on your site, download and install our 
			<a href="https://github.com/tunapanda/TI-wp-content-theme">theme</a>.
		</li>
		<li>
			Set the front page of your site to show the
			<a href="<?php
				echo admin_url("admin-ajax.php?action=install_swagtoc");
			?>">Swagpath Table of Contents</a>.
		</li>
	</ol>
</p>