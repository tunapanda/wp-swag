# wp-swag
Swag plugin
This is the main plugin of the swag system. This the plugin that enables the creation of swagpaths and swagifacts through shortcodes.

##Setup
* Clone the repo down to your wordpress plugin folder and enable the plugin in the admin menu.
* You can optionally use the [Github updator](https://github.com/afragen/github-updater) plugin to install it.


## How it works
Side note: This plugin takes advantage of the hierachial feature of wordpress pages and posts. The Home page serves as the parent page and has a list of all the Swagpaths grouped topic wise. Swagpaths are child pages of the Home page. In the Home page there is a shorcode [course-listing] that allows display of the grouped Swagpaths. 

####Creating Swagpaths
To create a new big topic example Astronomy, just create a new page and name it Astronomy. Then make Home page its parent page.</br> 
Now if you want to create a Swagpath under Astronomy, example History of Astronomy, just make another page and call it "History of astronomy". Make it a child page of the Astronomy page you created earlier. </br>
Under History of Astronomy you might want to create a Swagifact called Ancient Astromomy. To do so just put the following shortcodes in the editing area under the page History of Astronomy. 

[course]</br>
[h5p-course-item slug="ancient-astronomy"]</br>
[/course]</br>

This should look like this
![swagpath](https://github.com/tunapanda/wp-swag/blob/master/img/swagpath.png)	

Note: We use [H5P](https://h5p.org/) to create the content itself. Then we enclose the [h5p-course-item slug="ancient-astronomy"] to reference a given H5P item by its slug. Native H5P uses IDs to reference H5P items though. Using slug is an extended functionality from the main H5P plugin.

##Hacking !!
Feel free to dive in and help improove what we have so far :)

