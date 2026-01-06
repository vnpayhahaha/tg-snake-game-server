# 使用官方 Node.js 镜像，版本需符合 engines 要求（>=16.0.0）
FROM node:20.19.6

# 设置工作目录
WORKDIR /usr/src/app

# 复制 package.json 和 package-lock.json（如果有）
COPY package*.json ./

# 安装项目依赖
RUN npm install

# 复制项目所有文件到工作目录
COPY . .

# 暴露服务端口（根据你的 server.js 配置，假设为 8080）
EXPOSE 8080

# 定义容器启动时执行的命令（对应 package.json 的 "start": "node server.js"）
CMD ["npm", "start"]