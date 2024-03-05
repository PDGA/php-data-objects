### 2.0.1
* `ModelInstantiator#dataObjectToDatabaseModel` Now copies `NULL` from DB models to Data Objects.

### 2.0.0
* _BREAKING_: Moved reflection functions out of `ModelInstantiator` and into new class `ReflectionContainer`. Updated and added tests as appropriate.

### 1.5.1
* `ModelInstantiator` no longer passes `NULL`/`null` values to `Converter`s.
