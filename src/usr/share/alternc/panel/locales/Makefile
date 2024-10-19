all: cmsscanner.pot */LC_MESSAGES/cmsscanner.po

cmsscanner.pot: ../*/*.php
	[ -r $@ ] || touch $@
	xgettext --copyright-holder="AlternC Team" --package-name="AlternC" --package-version="2.0" --msgid-bugs-address="i18n@alternc.org"  --force-po -o $@ --keyword=__ --keyword=_  -L PHP -F --from-code UTF-8 $^

%/LC_MESSAGES/cmsscanner.po: cmsscanner.pot
	msgmerge -v -U $@ $^

