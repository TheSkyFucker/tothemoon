[TOC]

# Design principle

- 面向对象设计。
- 所有字段必须非空，空字段用默认字段代替。



## `user` 

  - **定位**：基类，拥有一些全局使用的属性。
  - **理解**：必须以 **对子类（具体场景具体定义）进行实例化** 来设计和理解，如 `activity.user` 。
  - **接口：** 维护 `user` 的属性。
  - **行为**：
        - 调用 `sign` 接口进行签到。
        - 申请进行 / 取消实例化子类。
        - 其余均为具体子类的行为。



## `sign` 

- **定位：**签到系统，全局静态类。
- **接口：** 维护 `sign.user` 的属性。
- **行为**：判定用户触发一些条件后，调用 `user` 的接口修改其属性。



## `position`

- **定位**：位置系统，全局静态类。
- **接口**：维护 `position.user` 。
- **行为**：无 



# User



## `user_base` 

| part                | mean   | type     | min / max | type | label |
| ------------------- | ------ | -------- | --------- | ---- | ----- |
| `username` （主键） | 用户名 | `string` |           |      |       |
| `password`          | 密码   | `string` |           |      |       |
| `realname`          | 姓名   | `string` |           |      |       |
| `role`              | 角色   | `string` |           |      |       |



## `user_detail` 

| part                | mean     | **type** | min / max | type | label |
| ------------------- | -------- | -------- | --------- | ---- | ----- |
| `username` （主键） | 用户名   | `string` |           |      |       |
| `sex`               | 性别     | `int`    |           |      |       |
| `born`              | 生日     | `date`   |           |      |       |
| `grade`             | 年级     | `int`    |           |      |       |
| `college`           | 学院     | `string` |           |      |       |
| `major`             | 专业     | `string` |           |      |       |
| `register`          | 注册时间 | `date`   |           |      |       |



# Sign



## `sign_user` 

| part               | mean             | type     | min / max | type | label |
| ------------------ | ---------------- | -------- | --------- | ---- | ----- |
| `username`（主键） | 用户名           | `string` |           |      |       |
| `last_sign`        | 上次签到         | `date`   |           |      |       |
| `begin_sign`       | 开始签到（未断） | `date`   |           |      |       |



## `sign_application` 

| part       | mean     | type     | min / max | type | label |
| ---------- | -------- | -------- | --------- | ---- | ----- |
| `id`       | 编号     | `int`    |           |      |       |
| `username` | 申请用户 | `string` |           |      |       |
| `date`     | 日期     | `date`   |           |      |       |

 

## `sign_log` 

| part       | mean     | type     | min / max | type | label |
| ---------- | -------- | -------- | --------- | ---- | ----- |
| `id`       | 记录编号 | `int`    |           |      |       |
| `username` | 签到用户 | `string` |           |      |       |
| `date`     | 日期     | `date`   |           |      |       |

















---



## `list`

- **定位**：抽象基类集
- **理解：**预置 **用户集**。用于且**仅用于** **批量添加** 想要实例化的 `user` 。
- **禁止：**将 `list` 作为 **实例** 代表用户集 进行设计。
  - 如：欲设计 `event` 的 参与用户，不能设计成将其用某个全局抽象的 `list` 代表。
  - 应：采取同 `activity` 类似的设计，在一个 `event` 实例中，通过 一些抽象的 `list` 以及 `user` 实例化一个 `activity.list` 。
- **备注**
  - 所以 `list` 并不影响正常功能的运作，不马上加。 



## `activity`

- **定位**：实例化对象，无抽象基类



# List

## list_base

| part          | mean     | type         | min / max | type | label                   |
| ------------- | -------- | ------------ | --------- | ---- | ----------------------- |
| `id` （主键） | 表单编号 | `int`        |           |      |                         |
| `title`       | 表单名称 | `string`     |           |      |                         |
| `statu`       | 状态     | `int`        |           |      | 默认1；开放1关闭0归档-1 |
| `member`      | 用户集合 | `arr2string` |           |      |                         |
| `application` | 申请队列 | `arr2string` |           |      |                         |
| `register`    | 注册时间 | `date`       |           |      |                         |
| `over`        | 归档时间 | `date`       |           |      |                         |
- 业务逻辑

> lv10 创建表单。
>
> lv9 以上开放注册（创建完成默认开放）。
>
> 用户申请 > 加入申请队列。
>
> 管理员处理 > 从申请队列删除 > 根据处理结果决定是否修改用户集合。

- 管理逻辑

> - lv9 以上关闭 / 开放注册。
> - 误操作 or 撤销 > 管理员选择删除列表中的某个用户 > 修改用户集合。
> - 批量修改名单内的人的状态

# Activity

# 



| part | mean | min | max | type | label |
