# !!! Warning: after changing version here, running OverState will do kernel upgrade on all machines at the same time,
# rebooting all servers at the same time. To avoid it, use salt '...' state.sls system.kernel_upgrade (one host at a time)
kernel:
  version: 3.13-0.bpo.1-amd64
  repository: wheezy-backports

rackspace:
  username: projectaventure
  apikey: 5bacf8c555ebd9a929b1880fa605beb2

filesystems:
  data:
    disk: /dev/xvde
    partition: 1
    filesystem: btrfs
    mount_point: /data
    mount_options: noatime,nobarrier,compress=lzo
# This example shows how to add volumes on specific machines - volumes must be first created in cloud admin panel!
{% if grains.id == 'some-wired-id-of-machine' %}
  data_elasticsearch:
    disk: /dev/xvdf
    partition: 1
    filesystem: ext4
    mount_point: /data/data_elasticsearch
    mount_option: noatime,nobarrier
{% endif %}

# Note - swapspace must be on ext3/ext4/xfs partitions, btrfs doesn't support swapspaces
swap:
  /SWAP:
    size: 2048
