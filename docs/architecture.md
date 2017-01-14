# Architecture

Swag is an open source project, and we are very happy to welcome contributions from other developers. This section includes information for developers who wish to help with the development.

As mentioned before, Swag is developed as a WordPress plugin. In order to help with the development it is therefore helpful if you have some experience with the development of such plugins.

Swag uses xAPI to store information about what students have learned and achieved. xAPI is an e-learning software specification that allows learning content and learning systems to speak to each other in a manner that records and tracks all types of learning experiences. In order to make installation easy, we have developed our own xAPI enabled Learning Record Store, but you can also use any other available Learning Record Store if you want, such as LearningLocker or lxHive.

Swag supports different types of learning resources. It uses a modular architecture in order to support the addition of new types of learning resources that can award Swag to a learner. Currently, we can work with the following types of learning resources:

* H5P - an open source project which allows authors to create and edit interactive videos, presentations, quizzes, games and more.
* Deliverables - Learners can submit coursework, such as written essays, photos or program source code and have it graded by a decentralized team of teachers or senior students. As the coursework have been reviewed, the learner will be awarded Swag.

It is possible to imagine other types of learning resources that would fit well in with the architecture. We are continuously working on adding more, and these are some types of learning resources we might support in the future.

* Learning resource created in using the SCORM standard.
* Presentations created in OpenOffice Impress or similar tools, such as Microsoft PowerPoint.
* We could let the learner connect his or her account to a MOOC, and import achievements and badges from there.

As mentioned before, Swag is developed as a WordPress plugin. Actually, it might be more accurate to say that it is a number of plugins. This is the list of plugins and their role within the larger system.

* Wp-swag - The main plugin that ties everything together. The features such as the rendering of swagmaps and showing the swagpaths is done here.
* Wp-deliverable - WordPress plugin that lets learners submit deliverables and have coaches review them. It is used in Swag to earn swag for coursework.
* Wp-remote-sync - Syncs content between the sites in a network of WordPress sites.
* Wp-xapi-lrs - An implementation of an XAPI learning record store.
* TI-wp-content-theme - Our WordPress theme.

If you want to help out with the development of Swag, please look at the list of issues for each plugin and repository on GitHub. You can also contact us, and we will be happy to explain more about the architecture and answer any questions you might have in a Skype call. There is an ongoing work with organising the issues and communicating them, if you want to help with this work it is also very welcome!