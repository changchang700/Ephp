##介绍

EPHP是基于Swoole开发的协程PHP开发框架，完美支持Http，WebSocket，TCP，UDP开发，拥有常驻内存，协程异步非阻塞IO等优点。

IMI框架文档丰富，上手容易，致力于让开发者跟使用传统MVC框架一样顺手。

IMI框架底层开发使用了强类型，易维护，性能更强。支持Aop，支持使用注解和配置文件注入，完全遵守PSR-3,4,7,11,15,16标准规范。

框架的扩展性强，开发者可以根据实际需求，自行开发相关驱动进行扩展。不止于框架本身提供的功能和组件！

>框架暂未实战验证，请无能力阅读和修改源代码的开发者，暂时不要用于实际项目开发，等待我们的实战检验完善，我们不希望因此为您造成不便！

###功能组件支持或者即将支持

-  [x]服务器（Http / Websocket / Tcp / Udp）
-  [x]容器（PSR-11）
-  [x] Aop注入
-  [x] Http中间件（PSR-15）
-  [x] MySQL连接池（协程＆同步，主从，负载均衡）
-  [x] Redis连接池（协程＆同步，负载均衡）
-  [x] Db连贯操作
-  [x]关系型数据库模型
-  [x]跨进程共享内存表模型
-  [x] Redis模型
-  [x]日志（PSR-3 /文件+控制台）
-  [x]缓存（PSR-16 / File + Redis）
-  [x]验证器（Valitation）
-  [x]任务异步任务
-  [x]进程/进程池
-  [x]命令行开发辅助工具
-  [x]业务代码热更新

>日志，缓存都支持：多驱动+多实例+统一操作入口
> 
>所有连接池都支持：同步+异步+多驱动+多实例

##运行环境

-  [PHP]（https://php.net/）> = 7.1
-  [作曲家]（https://getcomposer.org/）
-  [Swoole]（https://www.swoole.com/)>= 4.0.0（必须启用协程，如使用Redis请开启）
-  [Hiredis]（https://github.com/redis/hiredis/releases）（Swoole> = 4.2.6，无需独立编译）

##版权信息

IMI遵循Apache2开源协议发布，并提供免费使用。

<a href="https://opencollective.com/IMI/sponsor/0/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/0/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/1/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/1/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/2/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/2/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/3/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/3/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/4/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/4/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/5/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/5/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/6/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/6/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/7/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/7/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/8/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/8/avatar.svg” > </A>
<a href="https://opencollective.com/IMI/sponsor/9/website" target="_blank"> <img src =“https://opencollective.com/IMI/sponsor/9/avatar.svg” > </A>