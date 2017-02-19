# Nikon d3100 not connecting via USB for data transfer on linux mint. #

## Syslog shows ##
* kernel: [1592822.702088] usb 1-6.4: New USB device found, idVendor=04b0, idProduct=0427
* kernel: [1592822.702138] usb 1-6.4: New USB device strings: Mfr=1, Product=2, SerialNumber=3
* kernel: [1592822.702145] usb 1-6.4: Product: NIKON DSC D3100
* kernel: [1592822.702151] usb 1-6.4: Manufacturer: NIKON
* kernel: [1592822.702157] usb 1-6.4: SerialNumber: 000008539376
* org.gtk.vfs.GPhoto2VolumeMonitor[1841]: (process:2096): GVFS-GPhoto2-WARNING **: device (null) has no BUSNUM property, ignoring
* org.gtk.vfs.Daemon[1841]: ** (gvfsd:1960): WARNING **: dbus_mount_reply: Error from org.gtk.vfs.Mountable.mount(): No MTP devices found
* colord-sane: io/hpmud/pp.c 627: unable to read device-id ret=-1

## Solution was the following ##
sudo nano  /lib/udev/rules.d/69-libmtp.rules

ATTR{idVendor}=="04b0", ATTR{idProduct}=="0427",  MODE="666", GROUP="disk", ENV{ID_MTP_DEVICE}="1", PROGRAM="mtp-probe /sys$env{DEVPATH} $attr{busnum} $attr{devnum}", RESULT=="1", SYMLINK+="libmtp-%k", ENV{ID_M$
ATTR{idVendor}=="04b0", ATTR{idProduct}=="0428",  MODE="666", GROUP="disk", ENV{ID_MTP_DEVICE}="1", PROGRAM="mtp-probe /sys$env{DEVPATH} $attr{busnum} $attr{devnum}", RESULT=="1", SYMLINK+="libmtp-%k", ENV{ID_M$


Id vendor and idProduct extracted from syslogs above
