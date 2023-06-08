# Submission reflection #

A submission type that allows students to reflect on their assessments
The plugin offers the options to enable the reflection prior the grading of the assessment
or after


#### Declaration definition ####
![](/screenshots/assignsetting.JPG)


#### Student view ####
In this example the submissions reflection is enabled after grading
![](/screenshots/assignaftergradingstudentview.JPG)

In this example the submissions reflection is enabled before grading
![](/screenshots/assignbeforegradingstudentview.JPG)

Student view after saving a reflection
![](/screenshots/reflectionsubmittedstudentview.jpg)

#### Teacher view ####

Teacher view after student saved the reflection
![](/screenshots/reflectionsubmittedteacherview.jpg)

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/assign/submission/reflection

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 Veronica Bermegui

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
