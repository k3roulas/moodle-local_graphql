# moodle-local_graphql

Moodle plugin to expose Moodle API with GraphQL

Access URL http://localhost/local/graphql/client.html

TODO :
- explore the parameters to determine the max before creating TypeObject and InputTypeObject
 to allow positive increment in param and result objects (param_1, param_2 ...)
- type object naming convention : use lowercase
- refactor object creation by mutualizing TypeObject and InputTypeObject
- add scalars, add nullable, default
- add comments
- rework the criteria parameter : check other entry API to avoid the definition of the same type multiple times.
- write a resolver that find the right Moodle API to call
- when boostraping the API, call $context = \context_system::instance(); $PAGE->set_context($context);
- introspection of all Moodle API to generate the schema
- add a cache for the GraphQL schema
- add security layer

