// pages/api/socket.ts
import { Server as HttpServer } from 'http';
import { NextApiRequest, NextApiResponse } from 'next';
import { Server as SocketIOServer, Socket } from 'socket.io';

// Extend NextApiResponse to include the socket property
export interface NextApiResponseWithSocket extends NextApiResponse {
  socket: NextApiResponse['socket'] & {
    server: HttpServer & {
      io?: SocketIOServer;
    };
  };
}

// Global variable to hold the io instance (be careful with this in serverless environments)
// For Vercel, this approach works for dev but needs a custom server or external WebSocket service for prod.
let ioInstance: SocketIOServer | undefined;

export const getIO = (res?: NextApiResponseWithSocket): SocketIOServer | undefined => {
  if (res && res.socket.server.io) {
    return res.socket.server.io;
  }
  return ioInstance;
}

export default function handler(req: NextApiRequest, res: NextApiResponseWithSocket) {
  if (!res.socket.server.io) {
    console.log('*First use, starting Socket.IO');
    const httpServer = res.socket.server as HttpServer;
    const io = new SocketIOServer(httpServer, {
      path: '/api/socket_io', // Client will connect to this path
      addTrailingSlash: false,
      transports: ['websocket', 'polling'],
      cors: {
        origin: "*", // Be more specific in production
        methods: ["GET", "POST"]
      }
    });

    io.on('connection', (socket: Socket) => {
      console.log('Socket connected:', socket.id);
      socket.on('disconnect', () => {
        console.log('Socket disconnected:', socket.id);
      });
      // You can add specific event listeners here if needed,
      // e.g., socket.join('room_name');
    });

    res.socket.server.io = io;
    ioInstance = io; // Store instance
  } else {
    console.log('Socket.IO already running');
  }
  res.end();
}