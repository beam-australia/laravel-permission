---
title: PhpStorm Interaction
weight: 7
---

# Extending PhpStorm 

You may wish to extend PhpStorm to support Blade Directives of this package.

1. In PhpStorm, open Preferences, and navigate to **Languages and Frameworks -> PHP -> Blade**
(File | Settings | Languages & Frameworks | PHP | Blade)
2. Uncheck "Use default settings", then click on the `Directives` tab.
3. Add the following new directives for the laravel-permission package:


**group**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasGroup(`
- Suffix: `)); ?>`

--

**endgroup**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**hasgroup**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasGroup(`
- Suffix: `)); ?>`

--

**endhasgroup**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**hasanygroup**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasAnyGroup(`
- Suffix: `)); ?>`

--

**endhasanygroup**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**hasallgroups**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && auth()->user()->hasAllGroups(`
- Suffix: `)); ?>`

--

**endhasallgroups**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--

**unlessgroup**

- has parameter = YES
- Prefix: `<?php if(auth()->check() && !auth()->user()->hasGroup(`
- Suffix: `)); ?>`

--

**endunlessgroup**

- has parameter = NO
- Prefix: blank
- Suffix: blank

--
