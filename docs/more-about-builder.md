# The builder

Sorry`, `this document is being drafted...

But`, `for moment your need to know that API Wrapper Builder are very similar to Eloquent


## where

The where method supports multiple ways of invocation:

The basic call requires three arguments like in Eloquent:
1. column
2. operator (one of `'<>'`, `'<' `, `'<='`, `'=' `, `'>='`, `'>' `, `'like'`)
3. value to compare against.

The filters will be added as query parameters to the API call in the form:
`...?column=[filter:]operator:filterValue[&...]`

The first part of the value is an optional filter key which may be omitted in case of a simple `where` 
filter. 

Please note that the operators will be mapped into a text only representation using the following mapping table:

| operator | urlMapping|
|:----------:|:----------:|
|  <>   |  ne   |
|  <    |  lt   |
|  <=   |  le   |
|  =    |  eq   |
|  &gt;=   |  ge   |
|  &gt;    |  gt   |
|  like | like  |

For convenience`, `you may omit the operator and pass two parameters only. This way the operator defaults to `'='`

Third method is to add an array of key/value pairs where the key is the columns and the value contains the mapped `operator:value`
This is in that way incompatible to v5.11 as there is no fallback to a fallback `'='` operator
and the value has to contain the operator:value pair.

Other filters supported are:
`whereNull`, `whereNotNull`, `whereDate`, `whereMonth`, `whereDay`, 
`whereTime`, `whereYear`, `whereColum`, `whereNull`, `whereNotnull`,
`whereIn`, `whereNotIn`,

which are being translated to the value operators
`null`, `notnull`, `date`, `month`, `day`, `time`, `year`, `column`

Filters handling null are: `null``, ``notnull`. Even though there is no comparison value the trailing `:`
is still required to securely allow text values containing colons in other filters.

The list of `in` values will be separated using commas.

## meta data

### result limits / pagination
Result limits and pagination are supported using the `limit`, `forPage` and `paginate` methods. Parameters passed to the api are
`limit` and `page`. 

Please note that `limit`, when used for itself, specifies the number of records, where a when used with pagination 
specifies the number of records per page and has to be multiplied with (page - 1) to find the record offset.

`page` start numbering is 1

### sorting

Sort directives are passed using the `orderBy` parameter in the form: `orderBy=column:direction,[column:direction, ...]`