#!/bin/bash

if [[ -z "$1" ]]; then
  echo "❌ Error : missing version type"
  exit 1
fi

VERSION_TYPE="$1"

#get highest tag number
VERSION=$(git describe --abbrev=0 --tags 2> /dev/null)

#replace . with space so can split into an array
VERSION_BITS=(${VERSION//./ })

#get number parts and increase last one by 1
VNUM1=${VERSION_BITS[0]:-1}
VNUM2=${VERSION_BITS[1]:-0}
VNUM3=${VERSION_BITS[2]:-0}
VNUM1=$(echo $VNUM1 | sed 's/v//')

CURRENT_VERSION="$VNUM1.$VNUM2.$VNUM3"

if [ "$VERSION_TYPE" == 'major' ]; then
  VNUM1=$((VNUM1 + 1))
  VNUM2=0
  VNUM3=0
elif [ "$VERSION_TYPE" == 'minor' ]; then
  VNUM2=$((VNUM2 + 1))
  VNUM3=0
elif [ "$VERSION_TYPE" == 'patch' ]; then
  VNUM3=$((VNUM3 + 1))
else
  echo "❌ Error : invalid version type, allowed values are 'major', 'minor', 'patch'"
  exit 1
fi

NEXT_VERSION="$VNUM1.$VNUM2.$VNUM3"
NEXT_TAG="v$NEXT_VERSION"

echo "Updating version : $CURRENT_VERSION -> $NEXT_VERSION ($VERSION_TYPE) ..."

git tag -a "$NEXT_TAG" -m "$NEXT_VERSION ($VERSION_TYPE)" || exit 1
git push origin $NEXT_TAG || exit 1

echo "✅ Ok"
