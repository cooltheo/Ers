#!/usr/bin/env python
#coding=utf-8
import tornado.web

import tornado.httpserver
import tornado.ioloop
import tornado.options
from tornado.escape import json_encode, json_decode

import sys
import traceback

import config
import util

from tornado.options import define, options
define("port", default=80, help="run on the given port", type=int)
 
class Application(tornado.web.Application):
    def __init__(self):
        handlers = [
            (r"/_ping", PingHandler),
            (r"/(\w+)", IndexHandler),
            (r"/(\w+)/_scheme", SchemeHandler),
            (r"/(\w+)/_config", ConfigHandler)
        ]
        settings = dict(
            #template_path=os.path.join(os.path.dirname(__file__), "templates"),
            #static_path=os.path.join(os.path.dirname(__file__), "static"),
            debug=True,
        )
        
        #链接数据库
        self.db = util.Db(config.DB_MONGO_HOST, config.DB_MONGO_PORT, config.DB_MONGO_DBNAME)
        tornado.web.Application.__init__(self, handlers, **settings)
        
    
 
class IndexHandler(tornado.web.RequestHandler):
    def post(self, indexname):
        try:
            #参数检测
            data = self.get_argument("data");
            try:
                data = json_decode(data);
            except ValueError:
                raise Exception("Error parsing parameter '%s'" % "data")
            
            scheme = self.application.db.get_scheme(indexname)
            if not scheme:
                raise Exception("Undefined scheme of the %s" % indexname)
            
       
            #获取scheme中定义的primary_key
            primary_key = scheme.get("primary", config.SCHEME_DEFAULT_PRIMARY)
            #获取数据的主键ID
            pid = data.get(primary_key)
            pid = int(pid)
            data.pop(primary_key)
            
            if not primary:
                raise Exception("Primary key[%s] is not found" % primary_key)
         
            #TODO 检测其他数据
            record = self.application.db.get_data(indexname, pid)
            if record:
                #更新
                self.application.db.update_data(indexname, pid, data)
            else:
                #创建
                self.application.db.insert_data(indexname, pid, data)
            self.write("1")
        except tornado.web.HTTPError, e:
            self.write({"error_msg": e.log_message})
        except:
            print traceback.print_exc()
            error_msg = {"error_msg":  str(sys.exc_info()[1])}
            self.write(error_msg)
    
    def delete(self, indexname):
        try:
            #参数检测
            pid = self.request.headers.get("id")
            if pid:
                try:
                    pid = json_decode(pid)
                except:
                    raise Exception("Error parsing parameter '%s'" % "pids")
                if isinstance(pid, int) :
                    self._delete_id(indexname, pid)
                else:
                    self._delete_ids(indexname, pid)
            else:
                raise Exception("no valid parameters(%s)" % ("id"));
            self.write("1")
        except tornado.web.HTTPError, e:
            self.write({"error_msg": e.log_message})
        except:
            print traceback.print_exc()
            error_msg = {"error_msg":  str(sys.exc_info()[1])}
            self.write(error_msg)
        
    def _delete_id(self, indexname, pid):
        pid = int(pid)
        if pid <= 0:
            raise Exception("the parameter %s is not legal" % "id")
        record = self.application.db.get_data(indexname, pid)
        if record:
            #更新is_delete
            self.application.db.delete_data(indexname, pid, True)
        else:
            #创建记录
            self.application.db.delete_data(indexname, pid)
            
    def _delete_ids(self, indexname, pids):  
        if isinstance(pids, list):
                if isinstance(pid, int):
                    self._delete_id(indexname, pid)
        else:
            raise Exception("the parameter %s is not legal" % "ids")

class PingHandler(tornado.web.RequestHandler):
    def get(self):
        self.write("1");

class SchemeHandler(tornado.web.RequestHandler):
    def get(self, indexname):
        res = self.application.db.get_scheme(indexname)
        self.write(json_encode(res))
        
    def post(self, indexname):
        try:
            has_scheme = self.application.db.get_scheme(indexname)
            scheme = self.get_argument("scheme")
            
            try:
                scheme = json_decode(scheme)
            except:
                raise Exception("Error parsing parameter '%s'" % "scheme")
            
            #TODO 检测scheme是否合法
            if has_scheme:
                #更新
                self.application.db.update_scheme(indexname, scheme)
            else:
                #插入
                self.application.db.insert_scheme(indexname, scheme)
            self.write("1")
        except tornado.web.HTTPError, e:
            self.write({"error_msg": e.log_message})
        except:
            error_msg = {"error_msg": str(sys.exc_info()[0]) + " : " + str(sys.exc_info()[1])}
            self.write(error_msg)
            
    def delete(self, indexname):
        pass
        
            
class ConfigHandler(tornado.web.RequestHandler):
    def get(self, indexname):
        pass
    
    def post(self, indexname):
        pass
    
    
if __name__ == "__main__":
    tornado.options.parse_command_line()
    http_server = tornado.httpserver.HTTPServer(Application())
    http_server.listen(options.port)
    tornado.ioloop.IOLoop.instance().start()