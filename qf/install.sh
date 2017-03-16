#!/bin/bash

sudo cp ./qf /usr/bin/qf
sudo chmod +x /usr/bin/qf

sudo cp ./bash_completion.d/qf /etc/bash_completion.d/qf
. /etc/bash_completion
