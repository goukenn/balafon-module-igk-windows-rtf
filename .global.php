<?php
// @author: C.A.D. BONDJE DOUE
// @file: %modules%/igk/Windows/Rtf/.global.php
// @date: 20260127 09:16:15

// + module entry file 

use igk\Windows\Rtf\Converter\MarkdownToRtf; 

igk_environment()->push('markdown.converter', MarkdownToRtf::class);