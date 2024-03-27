### 4.0.0
* _BREAKING_: Adds a new interface called `IDatabaseModel` and updates the expected type of `$db_model` argument in `ModelInstantiator#databaseModelToDataObject` to be of that type, removing the ability to continue to supply arrays to that function. This interface is compatible with existing Eloquent models, so any caller already passing Eloquent models will be unaffected.

### 3.0.0
* _BREAKING_: Fixes a bug in `ModelInstantiator#databaseModelToDataObject`, introduced in 2.1.0, where nested relationships were skipped.  `databaseModelToDataObject` now calls `getRelations` if available to pull relationship data.  This change is potentially breaking because, by default, Eloquent changes the case of relations to snake case when an Eloquent model is converted to an array.

### 2.1.0
* Support passing Eloquent models to `ModelInstantiator#dataObjectToDatabaseModel` for efficiency.

### 2.0.1
* `ModelInstantiator#dataObjectToDatabaseModel` Now copies `NULL` from DB models to Data Objects.

### 2.0.0
* _BREAKING_: Moved reflection functions out of `ModelInstantiator` and into new class `ReflectionContainer`. Updated and added tests as appropriate.

### 1.5.1
* `ModelInstantiator` no longer passes `NULL`/`null` values to `Converter`s.
