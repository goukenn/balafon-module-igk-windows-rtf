# igk/Windows/Rtf

module that help transform .md (mardown) content to .rtf

## mark document with custom section 
in you .md file insert a special markup
```sh
[\section]{h:"Markup title %f_page-right%"}
```
- every title will have the `\keepn` format flag

usage via balafon cli 

```sh
balafon --rft:convert input_file.md ouput_file.rtf
```

@C.A.D.BONDJEDOUE