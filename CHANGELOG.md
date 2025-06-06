### 5.7.0
* Codesniffer Fixes.
* Added Xdebug.
* Changed IPrivacyProtectedDataObject name to ISensitiveDataObject to better communicate intent.

### 5.6.0
* Add back the `NotBlankValidator`.

### 5.5.0
* Allow data object relationship values to be null when nullable.
* Allow empty and whitespace strings for data object property values.

### 5.4.0
* Permits a boolean to be passed in to `dataObjectToArray()` to turn off privacy cleansing, in the case that the method will be used internally and not want to remove privacy fields.

### 5.3.0
* Created the IPrivacyProtectedDataObjectInterface. This interface will be implemented by data objects that need to have privacy protected data cleansed when the data object is converted to an array.

### 5.2.0
* Code styling fixes.

### 5.1.0
* Adds `JsonConverter` for database columns storing json data to have it automatically converted to php arrays.

### 5.0.0
* _BREAKING_: `DataObjectRelationshipParser` no longer supports nested circular relationships. These will now be considered invalid and will throw a `ValidationException`.

### 4.1.0
* Added `DataObjectRelationshipParser` to support validating the existence of specified relationships for a data object.

### 4.0.1
* Fixes the way `ModelInstantiator` handles `ManyToOne` relationships but checking if the data for the relationship is null and if so checking to ensure the relationship data is allowed to be null.  If not it throws an exception of type `InvalidRelationshipDataException`.

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
