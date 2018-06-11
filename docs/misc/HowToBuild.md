I prefer the setting below to do channel-discover and install in one command.

```
pear config-set auto_discover 1
```

## Prerequired

* PEAR_PackageFileManager2
* PHPUnit
* Phing
* ApiGen

```
pear install --alldeps -f PEAR_PackageFileManager2
pear install pear.phpunit.de/PHPUnit
pear install pear.phing.info/phing
pear install pear.apigen.org/apigen
```

## Build

Do:

```
phing
```