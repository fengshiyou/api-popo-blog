#!/bin/bash
rm -rf doc/* # 多次执行会导致页面可能无法显示的问题
rm -rf temp
mkdir temp  # 临时目录
# 遍历Controllers下面的文件,复制到temp临时目录
for i in app/Http/Controllers ; do
cp -r $i "temp/$(dirname $(dirname $i))"
done
# 使用apidoc工具生成html页面
apidoc -i temp -o doc
rm -r temp