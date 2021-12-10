#!/bin/bash

# example: OPENAPI_SPEC=openapi.yaml ./gen.sh typescript typeScriptSdkOutDir --additionalArg1 --addititionalArg2

LANG=$1
OUT_LOC=${2:-out/$LANG}
SPEC_LOC="https://raw.githubusercontent.com/ipfs/pinning-services-api-spec/master/ipfs-pinning-service.yaml"

echo "Generating $LANG"
echo "Using spec: $SPEC_LOC"

CFG_FILE="config/$LANG.yml"
CMD_CFG_FLAG="" && [[ -f $CFG_FILE ]] && CMD_CFG_FLAG="-c $CFG_FILE"
TEMPLATES_DIR="templates/$LANG"
CMD_TEMPLATES_FLAG="" && [[ -d $TEMPLATES_DIR ]] && CMD_TEMPLATES_FLAG="-t $TEMPLATES_DIR"

CMD="openapi-generator-cli generate \
-i $SPEC_LOC \
-g $LANG \
-o $OUT_LOC \
$CMD_CFG_FLAG \
$CMD_TEMPLATES_FLAG \
${@:3}"
echo "$CMD"
eval "$CMD"
