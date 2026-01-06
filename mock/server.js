/**
 * Mock TRON Wallet API Server
 * 模拟TronGrid API服务，用于开发和测试
 */

const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.MOCK_PORT || 8080;
const DATA_FILE = path.join(__dirname, 'data.json');

// 中间件
app.use(cors());
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public')));

// 数据操作
function loadData() {
    try {
        const data = fs.readFileSync(DATA_FILE, 'utf8');
        return JSON.parse(data);
    } catch (error) {
        return {
            wallets: {},
            transactions: [],
            config: { autoIncrementTxId: 1, defaultBlockHeight: 50000000, blockInterval: 3 }
        };
    }
}

function saveData(data) {
    fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 2));
}

// 生成交易哈希
function generateTxHash() {
    const chars = '0123456789abcdef';
    let hash = '';
    for (let i = 0; i < 64; i++) {
        hash += chars[Math.floor(Math.random() * chars.length)];
    }
    return hash;
}

// 地址转HEX格式
function addressToHex(address) {
    // 简化处理：T开头的Base58地址转为41开头的HEX
    return '41' + Buffer.from(address.slice(1)).toString('hex').slice(0, 40);
}

// TRX转SUN (1 TRX = 1,000,000 SUN)
function trxToSun(trx) {
    return Math.floor(trx * 1000000);
}

// SUN转TRX
function sunToTrx(sun) {
    return sun / 1000000;
}

// ==================== TronGrid API 模拟 ====================

/**
 * 获取账户交易历史
 * GET /v1/accounts/:address/transactions
 */
app.get('/v1/accounts/:address/transactions', (req, res) => {
    const { address } = req.params;
    const { only_confirmed, only_to, limit, min_timestamp } = req.query;

    const data = loadData();
    let transactions = data.transactions.filter(tx => {
        // 过滤接收地址
        if (only_to === 'true' && tx.to_address !== address) {
            return false;
        }
        // 过滤发送地址
        if (!only_to && tx.from_address !== address && tx.to_address !== address) {
            return false;
        }
        // 过滤时间戳
        if (min_timestamp && tx.block_timestamp < parseInt(min_timestamp)) {
            return false;
        }
        // 只返回已确认的
        if (only_confirmed === 'true' && tx.status !== 'SUCCESS') {
            return false;
        }
        return true;
    });

    // 按时间倒序
    transactions.sort((a, b) => b.block_timestamp - a.block_timestamp);

    // 限制数量
    const maxLimit = Math.min(parseInt(limit) || 200, 200);
    transactions = transactions.slice(0, maxLimit);

    // 转换为TronGrid格式
    // 注意：直接使用Base58地址，TronWebHelper会检测到已经是Base58格式并直接返回
    const tronGridData = transactions.map(tx => ({
        txID: tx.tx_hash,
        blockNumber: tx.block_height,
        block_timestamp: tx.block_timestamp,
        ret: [{ contractRet: tx.status }],
        raw_data: {
            contract: [{
                type: tx.contract_type || 'TransferContract',
                parameter: {
                    value: {
                        owner_address: tx.from_address,  // 直接使用Base58地址
                        to_address: tx.to_address,       // 直接使用Base58地址
                        amount: tx.amount
                    }
                }
            }]
        }
    }));

    res.json({
        success: true,
        data: tronGridData,
        meta: {
            at: Date.now(),
            page_size: maxLimit
        }
    });
});

/**
 * 获取账户信息
 * POST /wallet/getaccount
 */
app.post('/wallet/getaccount', (req, res) => {
    const { address } = req.body;
    const data = loadData();
    const wallet = data.wallets[address];

    if (!wallet) {
        res.json({ address: addressToHex(address), balance: 0 });
        return;
    }

    res.json({
        address: addressToHex(address),
        balance: wallet.balance,
        create_time: Date.now() - 86400000 * 30
    });
});

/**
 * 获取交易详情
 * POST /wallet/gettransactionbyid
 */
app.post('/wallet/gettransactionbyid', (req, res) => {
    const { value: txHash } = req.body;
    const data = loadData();
    const tx = data.transactions.find(t => t.tx_hash === txHash);

    if (!tx) {
        res.json({});
        return;
    }

    res.json({
        txID: tx.tx_hash,
        blockNumber: tx.block_height,
        block_timestamp: tx.block_timestamp,
        ret: [{ contractRet: tx.status }],
        raw_data: {
            contract: [{
                type: 'TransferContract',
                parameter: {
                    value: {
                        owner_address: addressToHex(tx.from_address),
                        to_address: addressToHex(tx.to_address),
                        amount: tx.amount
                    }
                }
            }]
        }
    });
});

/**
 * 获取当前区块
 * POST /wallet/getnowblock
 */
app.post('/wallet/getnowblock', (req, res) => {
    const data = loadData();
    const blockHeight = data.config.defaultBlockHeight +
        Math.floor((Date.now() - 1704067200000) / (data.config.blockInterval * 1000));

    res.json({
        blockID: generateTxHash(),
        block_header: {
            raw_data: {
                number: blockHeight,
                timestamp: Date.now()
            }
        }
    });
});

/**
 * HEX地址转Base58
 * POST /wallet/hextoaddress
 */
app.post('/wallet/hextoaddress', (req, res) => {
    const { value } = req.body;
    // 简化处理
    res.json({
        base58: 'T' + Buffer.from(value.slice(2), 'hex').toString().slice(0, 33).padEnd(33, 'x')
    });
});

// ==================== 管理API ====================

/**
 * 获取所有钱包
 */
app.get('/api/wallets', (req, res) => {
    const data = loadData();
    const wallets = Object.values(data.wallets).map(w => ({
        ...w,
        balance_trx: sunToTrx(w.balance)
    }));
    res.json({ success: true, data: wallets });
});

/**
 * 添加/更新钱包
 */
app.post('/api/wallets', (req, res) => {
    const { address, balance, label } = req.body;

    if (!address || !address.startsWith('T') || address.length !== 34) {
        return res.status(400).json({ success: false, message: '无效的TRON地址格式' });
    }

    const data = loadData();
    data.wallets[address] = {
        address,
        balance: trxToSun(parseFloat(balance) || 0),
        label: label || ''
    };
    saveData(data);

    res.json({ success: true, message: '钱包已保存' });
});

/**
 * 删除钱包
 */
app.delete('/api/wallets/:address', (req, res) => {
    const { address } = req.params;
    const data = loadData();

    if (data.wallets[address]) {
        delete data.wallets[address];
        saveData(data);
        res.json({ success: true, message: '钱包已删除' });
    } else {
        res.status(404).json({ success: false, message: '钱包不存在' });
    }
});

/**
 * 获取所有交易
 */
app.get('/api/transactions', (req, res) => {
    const data = loadData();
    const transactions = data.transactions.map(tx => ({
        ...tx,
        amount_trx: sunToTrx(tx.amount),
        time: new Date(tx.block_timestamp).toLocaleString('zh-CN')
    }));
    transactions.sort((a, b) => b.block_timestamp - a.block_timestamp);
    res.json({ success: true, data: transactions });
});

/**
 * 创建交易（模拟转账）
 */
app.post('/api/transactions', (req, res) => {
    const { from_address, to_address, amount_trx } = req.body;

    if (!from_address || !to_address) {
        return res.status(400).json({ success: false, message: '缺少地址参数' });
    }

    const amountSun = trxToSun(parseFloat(amount_trx) || 0);
    if (amountSun <= 0) {
        return res.status(400).json({ success: false, message: '金额必须大于0' });
    }

    const data = loadData();

    // 检查发送方余额
    if (data.wallets[from_address]) {
        if (data.wallets[from_address].balance < amountSun) {
            return res.status(400).json({ success: false, message: '余额不足' });
        }
        data.wallets[from_address].balance -= amountSun;
    }

    // 增加接收方余额
    if (data.wallets[to_address]) {
        data.wallets[to_address].balance += amountSun;
    }

    // 计算区块高度
    const blockHeight = data.config.defaultBlockHeight +
        Math.floor((Date.now() - 1704067200000) / (data.config.blockInterval * 1000));

    // 创建交易记录
    const tx = {
        tx_hash: generateTxHash(),
        from_address,
        to_address,
        amount: amountSun,
        block_height: blockHeight,
        block_timestamp: Date.now(),
        status: 'SUCCESS',
        contract_type: 'TransferContract'
    };

    data.transactions.push(tx);
    data.config.autoIncrementTxId++;
    saveData(data);

    res.json({
        success: true,
        message: '交易创建成功',
        data: {
            ...tx,
            amount_trx: sunToTrx(tx.amount)
        }
    });
});

/**
 * 删除交易
 */
app.delete('/api/transactions/:txHash', (req, res) => {
    const { txHash } = req.params;
    const data = loadData();

    const index = data.transactions.findIndex(tx => tx.tx_hash === txHash);
    if (index !== -1) {
        data.transactions.splice(index, 1);
        saveData(data);
        res.json({ success: true, message: '交易已删除' });
    } else {
        res.status(404).json({ success: false, message: '交易不存在' });
    }
});

/**
 * 清空所有交易
 */
app.delete('/api/transactions', (req, res) => {
    const data = loadData();
    data.transactions = [];
    saveData(data);
    res.json({ success: true, message: '所有交易已清空' });
});

// 启动服务
app.listen(PORT, () => {
    console.log(`\n🚀 Mock TRON API Server started`);
    console.log(`   API Server: http://localhost:${PORT}`);
    console.log(`   Web Console: http://localhost:${PORT}/index.html`);
    console.log(`\n📝 TronGrid API endpoints:`);
    console.log(`   GET  /v1/accounts/:address/transactions`);
    console.log(`   POST /wallet/getaccount`);
    console.log(`   POST /wallet/gettransactionbyid`);
    console.log(`   POST /wallet/getnowblock`);
    console.log(`\n🔧 Management API endpoints:`);
    console.log(`   GET/POST   /api/wallets`);
    console.log(`   DELETE     /api/wallets/:address`);
    console.log(`   GET/POST   /api/transactions`);
    console.log(`   DELETE     /api/transactions/:txHash`);
    console.log(`\n`);
});
