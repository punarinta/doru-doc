# Doru Documentor
An automatic documentor for APIs.

## Markup

Please see 'example' directory for better understanding.

Data types:
* "int" — creates an input limited to integers
* "date" — creates a date picker
* "bool" — creates a drop down box with "true" and "false"
* "enum" — creates a date down box with alternatives
* anything else — simply generates an text input field

Format

```
* @doc-var (int=42) foo!  - An obligatory integer named "foo" with default of 42.

* @doc-var (string) bar   - Just a string named "bar".
```

## Configuration file
Example:
```
{
  "input":
  {
    "dirs":
    [
      "../../App/Controller"
    ]
  },
  "output":
  {
    "dir": "../../public/docs",
    "filename": "index.html",
    "rootUrl":  "/api"
  },
  "exclude":
  [
    "Generic.php",
    "Upload.php"
  ],
  "templates":
  {
    "dir": "templates",
    "overwrite": ["header", "footer", "layout"]
  }
}
```

input.dirs — an array of relative paths to scan for files

output.dir — a relative path to the output directory
output.filename — name of the resulting HTML file
output.rootUrl — a root URL to use in in-page API calls

exclude — a list of files to exclude while scanning

templates.dir — a relative path to the directory with templates replacements
templates.overwrite — a list of templates to overwrite