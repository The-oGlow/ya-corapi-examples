#!/usr/bin/env bash
#
# This file is part of ezlogging
#
# (c) 2024 Oliver Glowa, coding.glowa.com
#
# This source file is subject to the Apache-2.0 license that is bundled
# with this source code in the file LICENSE.
#
checkStart() {
    cllcmd="${1}"
    if [[ ! ${cllcmd} == "bash" ]]; then
        echo -e "\nStart script from project-root with 'source ./install/loccomposer.sh'\n"
        exit 1;
    fi
}
checkStart "${0}"

# Define variables
Y_ROOT=$(realpath $(dirname ${0}))
Y_SRC_COMP=$(realpath ${Y_ROOT}/cfg/composer.json.dist)
Y_SRC_REPOS=$(realpath ${Y_ROOT}/cfg/composer-repos.json.dist)
Y_TARG=${Y_ROOT}/composer.json.loc

SRCH=#ph#
RPLC=""

echo -e "Check files"
if [[ -f ${Y_SRC_REPOS} ]]; then
    if [[ -f ${Y_TARG} ]]; then
        echo -e "Override: ${Y_TARG}"
    fi
    cp ${Y_SRC_COMP} ${Y_TARG}

    echo -e "Load replacement"
    RPLC=$(cat ${Y_SRC_REPOS})
    printf -v RPLC "%Q" ${RPLC}
    RPLC=$(echo "${RPLC}"|sed 's/\//\\\//g')

    echo -e "Add replacement"
    sed -i -e "s/${SRCH}/${RPLC}/g" ${Y_TARG}

    export COMPOSER="${Y_TARG}"

    printenv|grep "COMPOSER"

else 
    echo -e "File missing: ${Y_SRC_REPOS}"
fi

echo -e "\nFinished\n"
