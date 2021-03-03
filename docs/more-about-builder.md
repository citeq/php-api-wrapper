# The builder

Sorry, this document is being drafted...

But, for moment your need to know that API Wrapper Builder are very similar to Eloquent


## where

The where method supports multiple ways of invocation:

The basic call requires three arguments like in Eloquent:
1. column
2. operator (one of `'<>', '<' , '<=', '=' , '>=', '>' , 'like'`)
3. value to compare against.

The filters will be added as query parameters to the API call in the form:
`...?column=operator:filterValue[&...]`
Pleas note that the operators will be mapped into a text only representation using the following mapping table:


| operator | urlMapping|
|:----------:|:----------:|
|  <>   |  ne   |
|  <    |  lt   |
|  <=   |  le   |
|  =    |  eq   |
|  &gt;=   |  ge   |
|  &gt;    |  gt   |
|  like | like  |

For convenience, you may omit the operator and pass two parameters only. This way the operator defaults to `'='`

Third method is to add an array of key/value pairs where the key is the columns and the value contains the mapped `operator:value`
This is in that way incompatible to v5.11 as there is no fallback to a fallback `'='` operator
and the value has to contain the operator:value pair.

