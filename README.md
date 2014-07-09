Lean
====
The PHP framework for simple, REST API backends. It gets out of your way, so you can get your work done.
Quite similar to [Laravel](github.com/laravel/laravel), but much easier, only not as matured.
The overall objective is to achieve less clutter on the server, and to move as much application logic to the client.
Hence, this is best suited to provisionign JSON APIs, at the moment.

#### As used in:
* [Annals of Tropical Pathology](http://annalsoftropicalpathology.org)
* [Ultractiv](ultractiv.com.ng)
* [Public Interest in Corruption Cases](http://picc.com.ng)

#### Drawbacks:
* Does not currently handle views

#### Requirements:
* PHP `>=5.4.14`
* Apache Server (with `mod_rewrite`)
* Nginx Server (as alternative to Apache)

#### TODO:
* Rename the framework from Lean
* Create proper documentation - Use Github pages
* Create a getting started guide - Use Github pages
* Drop the logging and use `Monolog/Monolog` instead
* Drop explicit declaration of production config, and read from `ENV` vars instead
* Refactor file upload logic to support AWS
* Refactor `Model/Base` inheritance to drop the use of `Model/Traits`
* Use an inflection library to pluralize/singularize model names
* Refactor `Controller` to include a `__magic` method that automatically implements actions on matched RESTful CRUD routes
* Implement the `View` class
* Integrate a templating library to handle view rendering
* Extend `Model/Base` to handle authentication and password encryption (using `bcrypt`)
* Extend `Model/Base` to take care of virtual attributes
* Add an application generator script (bash/dll) so that skeleton can be built from commandline
* and many more

#### Creator:
* [Yemi Agbetunsin](github.com/temiyemi)
