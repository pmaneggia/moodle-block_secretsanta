# Block Secret Santa `block_secretsanta` (Wichteln)

### WIP - not ready yet, but it is already usable!

It is this time of the year again! Do Secret Santa in a Moodle course with this plugin!

#### How it works
Add this block to a course to do Sectet Santa among the users enrolled in the course.

Normal users enrolled in the course see the result of the draw that is relevant for themeselves or a text saying that the draw did not take place yet.

Priviledged users, normally teachers (role `editingteacher`), can perform the actions "draw" and "reset". They do however not see the global result of the draw, so that they can take part without spoilers. Users with more rights, normally managers (role `manager`), can also view the result of the draw.

##### New feature: select participating users

It is now possible for a teacher to select which users among the ones enrolled in the course will participate in the draw.

By default all users are selected as participating. Resetting will also restore this default configuration.

In some future versions there will be a graphical representation of the result of the draw.


#### For site administators
The visibility described in the section **How it works** is controlled by capabilites (`secretsanta:draw` by default set to allow for `editingteacher` and manager and `secretsanta:canviewresult` set to allow for role `manager`).


#### Installation
1. Copy the content of this directory into the folder `blocks/secretsanta` inside your moodle installation.
1. Go to the _Site administration -> Notifications_ to start the install process.

#### Supported Moodle versions
The block supports Moodle 4.

#### License
2022 Paola Maneggia

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
