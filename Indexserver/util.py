#!/usr/bin/env python
#coding=utf-8
import config;
import pymongo;

class Db:
    def __init__(self, host, port, dbname):
        conn = pymongo.Connection(host, port)
        self.db = conn[dbname]
    
    def get_scheme(self, indexname):
        col = self.db[config.COL_CONFIG_NAME]
        query = {"index_name" : indexname}
        fields = {"index_name" : 1, "scheme" : 1}
        res = col.find_one(query, fields)
        if res:
            res.pop('_id')
            if not res.has_key('scheme'):
                res = None
        return res
    
    def insert_scheme(self, indexname, scheme):
        col = self.db[config.COL_CONFIG_NAME]
        data = {"index_name" : indexname, "scheme" : scheme}
        res = col.insert(data)
        return res
    
    def update_scheme(self, indexname, schme):
        col = self.db[config.COL_CONFIG_NAME]
        query = {"index_name" : indexname}
        data = {"$set" : {"scheme" : scheme}}
        res = col.update(query, data)
        return res
    
    
    def get_data(self, indexname, pid):
        col = self.db[indexname]
        query = {'pid': pid}
        res = col.find_one(query)
        if res:
            res.pop('_id')
        return res
    
    def update_data(self, indexname, pid, data):
        col = self.db[indexname]
        query = {'pid': pid}
        data = {"$set" : {"data" : data}}
        res = col.update(query, data)
        return res
        
    def insert_data(self, indexname, pid, data):
        col = self.db[indexname]
        data = {'is_deleted' : False, 'data':data, 'pid': pid}
        res = col.insert(data)
        return res
    
    def delete_data(self, indexname, pid, update = False):
        col = self.db[indexname]
        if update:
            query = {'pid': pid}
            data = {"$set" : {'is_deleted' : True}}
            res = col.update(query, data)
        else:
            data = {'is_deleted' : True, 'data':None, 'pid': pid}
            res = col.insert(data)
        return res
    

        
        